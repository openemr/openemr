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

//=========================================================================
//  The following define is necessary for the date and time functions
//  set it to the locale for this language
//=========================================================================
define('_PC_LOCALE', 'en_US');
//=========================================================================
//  Defines used in all files
//=========================================================================
// new in 3.9.9
define('_PC_NOTIFY_ADMIN', 'Notify Admin About Event Submission/Modification?');
define('_PC_NOTIFY_EMAIL', 'Admin Email Address');
define('_PC_NOTIFY_UPDATE_MSG', "The following calendar event has been modifed:\n\n");
define('_PC_NOTIFY_NEW_MSG', "The following calendar event has been added:\n\n");
define('_PC_NOTIFY_SUBJECT', 'NOTICE:: PostCalendar Submission/Modification');
//...
define('_POSTCALENDARNOAUTH', 'Not authorised to access PostCalendar module');
define('_POSTCALENDAR_NOAUTH', 'Not authorised to access PostCalendar module');
define('_PC_CAN_NOT_EDIT', 'You are not allowed to edit this event');
define('_PC_CAN_NOT_DELETE', 'You are not allowed to delete this event');
define('_PC_DELETE_ARE_YOU_SURE', 'Are you sure you want to delete this event?');
define('_PC_ADMIN_YES', 'Yes');
define('_PC_FILTER_USERS', 'Default/Global');
define('_PC_FILTER_USERS_ALL', 'All Users');
define('_PC_FILTER_CATEGORY', 'All Categories');
define('_PC_FILTER_TOPIC', 'All Topics');
define('_USER_BUSY_TITLE', 'Busy');
define('_USER_BUSY_MESSAGE', 'I am busy during this time.');
define('_PC_JUMP_MENU_SUBMIT', 'go');
define('_PC_TPL_VIEW_SUBMIT', 'change');
define('_PC_SUBMIT_TEXT', 'Plain Text');
define('_PC_SUBMIT_HTML', 'HTML');
define('_CALJAN', 'January');
define('_CALFEB', 'February');
define('_CALMAR', 'March');
define('_CALAPR', 'April');
define('_CALMAY', 'May');
define('_CALJUN', 'June');
define('_CALJUL', 'July');
define('_CALAUG', 'August');
define('_CALSEP', 'September');
define('_CALOCT', 'October');
define('_CALNOV', 'November');
define('_CALDEC', 'December');
define('_CALPREVIOUS', 'Prev');
define('_CALNEXT', 'Next');
define('_CALLONGFIRSTDAY', 'Sunday');
define('_CALLONGSECONDDAY', 'Monday');
define('_CALLONGTHIRDDAY', 'Tuesday');
define('_CALLONGFOURTHDAY', 'Wednesday');
define('_CALLONGFIFTHDAY', 'Thurdsay');
define('_CALLONGSIXTHDAY', 'Friday');
define('_CALLONGSEVENTHDAY', 'Saturday');
define('_CALMONDAYSHORT', 'M');
define('_CALTUESDAYSHORT', 'T');
define('_CALWEDNESDAYSHORT', 'W');
define('_CALTHURSDAYSHORT', 'T');
define('_CALFRIDAYSHORT', 'F');
define('_CALSATURDAYSHORT', 'S');
define('_CALSUNDAYSHORT', 'S');
define('_CALSUNDAY', 'Sunday');
define('_CALMONDAY', 'Monday');
define('_CALTUESDAY', 'Tuesday');
define('_CALWEDNESDAY', 'Wednesday');
define('_CALTHURSDAY', 'Thursday');
define('_CALFRIDAY', 'Friday');
define('_CALSATURDAY', 'Saturday');
define('_CAL_DAYVIEW', 'Day');
define('_CAL_WEEKVIEW', 'Week');
define('_CAL_MONTHVIEW', 'Month');
define('_CAL_YEARVIEW', 'Year');
define('_PC_NEW_EVENT_HEADER', 'Event');
define('_PC_DATE_TIME', 'Event Date');
define('_PC_ALLDAY_EVENT', 'All day event');
define('_PC_TIMED_EVENT', 'Timed event');
define('_PC_EVENT_TYPE', 'Event Category');
define('_PC_SHARING', 'Sharing');
define('_PC_EVENT_TOPIC', 'Topic');
define('_PC_SHARE_PRIVATE', 'Private');
define('_PC_SHARE_PUBLIC', 'Public');
define('_PC_SHARE_SHOWBUSY', 'Show as Busy');
define('_PC_SHARE_GLOBAL', 'Global');
define('_PC_EVENT_STREET', 'Street');
define('_PC_EVENT_CITY', 'City');
define('_PC_EVENT_STATE', 'State');
define('_PC_EVENT_POSTAL', 'Postal');
define('_PC_EVENT_CONTACT', 'Contact');
define('_PC_EVENT_PHONE', 'Phone');
define('_PC_EVENT_EMAIL', 'Email');
define('_PC_REPEATING_HEADER', 'Repeating Info:');
define('_PC_NO_REPEAT', 'Event does not repeat');
define('_PC_REPEAT', 'Event repeats every');
define('_PC_REPEAT_ON', 'Event repeats on');
define('_PC_OF_THE_MONTH', 'of the month every');
define('_PC_END_DATE', 'End date');
define('_PC_NO_END', 'No end date');
define('_PC_TIMED_DURATION', 'Duration');
define('_PC_TIMED_DURATION_HOURS', 'Hours');
define('_PC_TIMED_DURATION_MINUTES', 'Minutes');
define('_PC_EVERY', 'Every');
define('_PC_EVERY_OTHER', 'Every Other');
define('_PC_EVERY_THIRD', 'Every Third');
define('_PC_EVERY_FOURTH', 'Every Fourth');
define('_PC_EVERY_1ST', 'First');
define('_PC_EVERY_2ND', 'Second');
define('_PC_EVERY_3RD', 'Third');
define('_PC_EVERY_4TH', 'Fourth');
define('_PC_EVERY_LAST', 'Last');
define('_PC_EVERY_SUN', 'Sun');
define('_PC_EVERY_MON', 'Mon');
define('_PC_EVERY_TUE', 'Tue');
define('_PC_EVERY_WED', 'Wed');
define('_PC_EVERY_THU', 'Thu');
define('_PC_EVERY_FRI', 'Fri');
define('_PC_EVERY_SAT', 'Sat');
define('_PC_OF_EVERY_MONTH', 'month');
define('_PC_OF_EVERY_2MONTH', 'other month');
define('_PC_OF_EVERY_3MONTH', '3 months');
define('_PC_OF_EVERY_4MONTH', '4 months');
define('_PC_OF_EVERY_6MONTH', '6 months');
define('_PC_OF_EVERY_YEAR', 'year');
define('_PC_EVERY_DAY', 'Day(s)');
define('_PC_EVERY_WORKDAY', 'Day(s) M-F');
define('_PC_EVERY_WEEK', 'Week(s)');
define('_PC_EVERY_MONTH', 'Month(s)');
define('_PC_MONTHS', 'Month(s)');
define('_PC_EVERY_YEAR', 'Year(s)');
define('_PC_EVERY_MWF', 'Mon, Wed &amp; Fri');
define('_PC_EVERY_TR', 'Tues &amp; Thur');
define('_PC_EVERY_MF', 'Mon thru Fri');
define('_PC_EVERY_SS', 'Sat &amp; Sun');
define('_PC_EVENT_LOCATION', 'Event Location');
define('_PC_EVENT_CONTNAME', 'Contact Person');
define('_PC_EVENT_CONTTEL', 'Contact Phone Number');
define('_PC_EVENT_CONTEMAIL', 'Contact Email');
define('_PC_EVENT_WEBSITE', 'Event Website');
define('_PC_EVENT_FEE', 'Event Fee');
define('_PC_EVENT_PREVIEW', 'Preview Event');
define('_PC_EVENT_SUBMIT', 'Submit Event');
define('_PC_EVENT_TITLE', 'Event Title');
define('_PC_EVENT_DESC', 'Event Description');
define('_PC_EVENT_CATEGORY', 'Event Category');
define('_PC_LIMIT_TITLE', 'Limit Of Events');
define('_PC_LIMIT_START_TIME', 'Start Time');
define('_PC_LIMIT_END_TIME', 'End Time');
define('_PC_TOPIC', 'Topic');
define('_PC_REQUIRED', '*Required');
define('_PC_AM', 'AM');
define('_PC_PM', 'PM');
define('_PC_EVENT_SUBMISSION_FAILED', 'Your submission failed.');
define('_PC_EVENT_SUBMISSION_SUCCESS', 'Your event has been submitted.');
define('_PC_EVENT_EDIT_SUCCESS', 'Your event has been modified.');
define('_PC_SUBMIT_ERROR', 'There are errors with your submission.  These are outlined below.');
define('_PC_SUBMIT_ERROR1', 'Your start date is greater than your end date');
define('_PC_SUBMIT_ERROR2', 'Your start date is invalid');
define('_PC_SUBMIT_ERROR3', 'Your end date is invalid');
define('_PC_SUBMIT_ERROR4', 'is a required field.');
define('_PC_SUBMIT_ERROR5', 'Your repeating frequency must be at least 1.');
define('_PC_SUBMIT_ERROR6', 'Your repeating frequency must be an integer.');
define('_PC_ADMIN_EVENT_ERROR', 'There was an error while processing your request.');
define('_PC_ADMIN_EVENTS_DELETED', 'Your event has been deleted.');
@ define('_NO_DIRECT_ACCESS', 'You can not access this function directly.');
?>
