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
define('_PC_LOCALE', xl ('_PC_LOCALE') );
//=========================================================================
//  Defines used in all files
//=========================================================================
// new in 3.9.9
define('_PC_NOTIFY_ADMIN', xl ('Notify Admin About Event Submission/Modification?') );
define('_PC_NOTIFY_EMAIL', xl ('Admin Email Address') );
define('_PC_NOTIFY_UPDATE_MSG', xl("The following calendar event has been modifed:", "", "", "\n\n") );
define('_PC_NOTIFY_NEW_MSG', xl( "The following calendar event has been added:", "", "", "\n\n") );
define('_PC_NOTIFY_SUBJECT', xl ('NOTICE:: PostCalendar Submission/Modification') );
//...
define('_POSTCALENDARNOAUTH', xl ('Not authorised to access PostCalendar module') );
define('_POSTCALENDAR_NOAUTH', xl ('Not authorised to access PostCalendar module') );
define('_PC_CAN_NOT_EDIT', xl ('You are not allowed to edit this event') );
define('_PC_CAN_NOT_DELETE', xl ('You are not allowed to delete this event') );
define('_PC_DELETE_ARE_YOU_SURE', xl ('Are you sure you want to delete this event?') );
define('_PC_ADMIN_YES', xl ('Yes') );
define('_PC_FILTER_USERS', xl ('Default/Global') );
define('_PC_FILTER_USERS_ALL', xl ('All Users') );
define('_PC_FILTER_CATEGORY', xl ('All Categories') );
define('_PC_FILTER_TOPIC', xl ('All Topics') );
define('_USER_BUSY_TITLE', xl ('Busy') );
define('_USER_BUSY_MESSAGE', xl ('I am busy during this time.') );
define('_PC_JUMP_MENU_SUBMIT', xl ('go') );
define('_PC_TPL_VIEW_SUBMIT', xl ('change') );
define('_PC_SUBMIT_TEXT', xl ('Plain Text') );
define('_PC_SUBMIT_HTML', xl ('HTML') );
define('_CALJAN', xl ('January') );
define('_CALFEB', xl ('February') );
define('_CALMAR', xl ('March') );
define('_CALAPR', xl ('April') );
define('_CALMAY', xl ('May') );
define('_CALJUN', xl ('June') );
define('_CALJUL', xl ('July') );
define('_CALAUG', xl ('August') );
define('_CALSEP', xl ('September') );
define('_CALOCT', xl ('October') );
define('_CALNOV', xl ('November') );
define('_CALDEC', xl ('December') );
define('_CALPREVIOUS', xl ('Prev') );
define('_CALNEXT', xl ('Next') );
define('_CALLONGFIRSTDAY', xl ('Sunday') );
define('_CALLONGSECONDDAY', xl ('Monday') );
define('_CALLONGTHIRDDAY', xl ('Tuesday') );
define('_CALLONGFOURTHDAY', xl ('Wednesday') );
define('_CALLONGFIFTHDAY', xl ('Thursday') );
define('_CALLONGSIXTHDAY', xl ('Friday') );
define('_CALLONGSEVENTHDAY', xl ('Saturday') );
define('_CALMONDAYSHORT', 'M');
define('_CALTUESDAYSHORT', 'T');
define('_CALWEDNESDAYSHORT', 'W');
define('_CALTHURSDAYSHORT', 'T');
define('_CALFRIDAYSHORT', 'F');
define('_CALSATURDAYSHORT', 'S');
define('_CALSUNDAYSHORT', 'S');
define('_CALSUNDAY', xl ('Sunday') );
define('_CALMONDAY', xl ('Monday') );
define('_CALTUESDAY', xl ('Tuesday') );
define('_CALWEDNESDAY', xl ('Wednesday') );
define('_CALTHURSDAY', xl ('Thursday') );
define('_CALFRIDAY', xl ('Friday') );
define('_CALSATURDAY', xl ('Saturday') );
define('_CAL_DAYVIEW', xl ('Day') );
define('_CAL_WEEKVIEW', xl ('Week') );
define('_CAL_MONTHVIEW', xl ('Month') );
define('_CAL_YEARVIEW', xl ('Year') );
define('_PC_NEW_EVENT_HEADER', xl ('Event') );
define('_PC_DATE_TIME', xl ('Event Date') );
define('_PC_ALLDAY_EVENT', xl ('All day event') );
define('_PC_TIMED_EVENT', xl ('Timed event') );
define('_PC_EVENT_TYPE', xl ('Event Category') );
define('_PC_SHARING', xl ('Sharing') );
define('_PC_EVENT_TOPIC', xl ('Topic') );
define('_PC_SHARE_PRIVATE', xl ('Private') );
define('_PC_SHARE_PUBLIC', xl ('Public') );
define('_PC_SHARE_SHOWBUSY', xl ('Show as Busy') );
define('_PC_SHARE_GLOBAL', xl ('Global') );
define('_PC_EVENT_STREET', xl ('Street') );
define('_PC_EVENT_CITY', xl ('City') );
define('_PC_EVENT_STATE', xl ('State') );
define('_PC_EVENT_POSTAL', xl ('Postal') );
define('_PC_EVENT_CONTACT', xl ('Contact') );
define('_PC_EVENT_PHONE', xl ('Phone') );
define('_PC_EVENT_EMAIL', xl ('Email') );
define('_PC_REPEATING_HEADER', xl ('Repeating Info:') );
define('_PC_NO_REPEAT', xl ('Event does not repeat') );
define('_PC_REPEAT', xl ('Event repeats every') );
define('_PC_REPEAT_ON', xl ('Event repeats on') );
define('_PC_OF_THE_MONTH', xl ('of the month every') );
define('_PC_END_DATE', xl ('End date') );
define('_PC_NO_END', xl ('No end date') );
define('_PC_TIMED_DURATION', xl ('Duration') );
define('_PC_TIMED_DURATION_HOURS', xl ('Hours') );
define('_PC_TIMED_DURATION_MINUTES', xl ('Minutes') );
define('_PC_EVERY', xl ('Every') );
define('_PC_EVERY_OTHER', xl ('Every Other') );
define('_PC_EVERY_THIRD', xl ('Every Third') );
define('_PC_EVERY_FOURTH', xl ('Every Fourth') );
define('_PC_EVERY_1ST', xl ('First') );
define('_PC_EVERY_2ND', xl ('Second') );
define('_PC_EVERY_3RD', xl ('Third') );
define('_PC_EVERY_4TH', xl ('Fourth') );
define('_PC_EVERY_LAST', xl ('Last') );
define('_PC_EVERY_SUN', xl ('Sun') );
define('_PC_EVERY_MON', xl ('Mon') );
define('_PC_EVERY_TUE', xl ('Tue') );
define('_PC_EVERY_WED', xl ('Wed') );
define('_PC_EVERY_THU', xl ('Thu') );
define('_PC_EVERY_FRI', xl ('Fri') );
define('_PC_EVERY_SAT', xl ('Sat') );
define('_PC_OF_EVERY_MONTH', xl ('month') );
define('_PC_OF_EVERY_2MONTH', xl ('other month') );
define('_PC_OF_EVERY_3MONTH', xl ('3 months') );
define('_PC_OF_EVERY_4MONTH', xl ('4 months') );
define('_PC_OF_EVERY_6MONTH', xl ('6 months') );
define('_PC_OF_EVERY_YEAR', xl ('year') );
define('_PC_EVERY_DAY', xl ('Day(s)') );
define('_PC_EVERY_WORKDAY', xl ('Day(s) M-F') );
define('_PC_EVERY_WEEK', xl ('Week(s)') );
define('_PC_EVERY_MONTH', xl ('Month(s)') );
define('_PC_MONTHS', xl ('Month(s)') );
define('_PC_EVERY_YEAR', xl ('Year(s)') );
define('_PC_EVERY_MWF', xl ('Mon, Wed &amp; Fri') );
define('_PC_EVERY_TR', xl ('Tues &amp; Thur') );
define('_PC_EVERY_MF', xl ('Mon thru Fri') );
define('_PC_EVERY_SS', xl ('Sat &amp; Sun') );
define('_PC_EVENT_LOCATION', xl ('Event Location') );
define('_PC_EVENT_CONTNAME', xl ('Contact Person') );
define('_PC_EVENT_CONTTEL', xl ('Contact Phone Number') );
define('_PC_EVENT_CONTEMAIL', xl ('Contact Email') );
define('_PC_EVENT_WEBSITE', xl ('Event Website') );
define('_PC_EVENT_FEE', xl ('Event Fee') );
define('_PC_EVENT_PREVIEW', xl ('Preview Event') );
define('_PC_EVENT_SUBMIT', xl ('Submit Event') );
define('_PC_EVENT_TITLE', xl ('Event Title') );
define('_PC_EVENT_DESC', xl ('Event Description') );
define('_PC_EVENT_CATEGORY', xl ('Event Category') );
define('_PC_LIMIT_TITLE', xl ('Limit Of Events') );
define('_PC_LIMIT_START_TIME', xl ('Start Time') );
define('_PC_LIMIT_END_TIME', xl ('End Time') );
define('_PC_TOPIC', xl ('Topic') );
define('_PC_REQUIRED', xl ('*Required') );
define('_PC_AM', xl ('AM') );
define('_PC_PM', xl ('PM') );
define('_PC_EVENT_SUBMISSION_FAILED', xl ('Your submission failed.') );
define('_PC_EVENT_SUBMISSION_SUCCESS', xl ('Your event has been submitted.') );
define('_PC_EVENT_EDIT_SUCCESS', xl ('Your event has been modified.') );
define('_PC_SUBMIT_ERROR', xl ('There are errors with your submission.  These are outlined below.') );
define('_PC_SUBMIT_ERROR1', xl ('Your start date is greater than your end date') );
define('_PC_SUBMIT_ERROR2', xl ('Your start date is invalid') );
define('_PC_SUBMIT_ERROR3', xl ('Your end date is invalid') );
define('_PC_SUBMIT_ERROR4', xl ('is a required field.') );
define('_PC_SUBMIT_ERROR5', xl ('Your repeating frequency must be at least 1.') );
define('_PC_SUBMIT_ERROR6', xl ('Your repeating frequency must be an integer.') );
define('_PC_ADMIN_EVENT_ERROR', xl ('There was an error while processing your request.') );
define('_PC_ADMIN_EVENTS_DELETED', xl ('Your event has been deleted.') );
@ define('_NO_DIRECT_ACCESS', xl ('You can not access this function directly.') );
define('_PC_CAT_PATIENT', xl ('Patient') );
define('_PC_CAT_PROVIDER', xl ('Provider') );
?>
