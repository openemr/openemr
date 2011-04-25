<?php 
/*
 * Id: admin.php,v 1.1.1.3 2005/06/23 05:33:20 drbowen Exp $
 *
 * PostCalendar::PostNuke Events Calendar Module
 * Copyright (C) 2002 xl (The PostCalendar Team
 * http://postcalendar.tv
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. xl (See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA xl (02111-1307 xl (USA
 *
 * To read the license please read the docs/license.txt or visit
 * http://www.gnu.org/copyleft/gpl.html
 *
 */

//=========================================================================
// xl (ok, here's the rest of the language defines
//=========================================================================

define('_PC_UPDATED', xl ('Your PostCalendar configuration has been updated.') );
define('_PC_UPDATED_DEFAULTS', xl ('Your PostCalendar configuration has been reset to use defaults.') );
define('_POSTCALENDAR', xl ('PostCalendar Administration') );
define('_PC_ADMIN_GLOBAL_SETTINGS', xl ('PostCalendar Global Settings' , '', '<b>', '</b>') );
define('_PC_ADMIN_CATEGORY_SETTINGS', xl ('PostCalendar Category Settings', '', '<b>', '</b>') );
define('_PC_APPROVED_ADMIN', xl ('Approved Events Administration') );
define('_PC_HIDDEN_ADMIN', xl ('Hidden Events Administration') );
define('_PC_QUEUED_ADMIN', xl ('Queued Events Administration') );
define('_PC_NO_EVENT_SELECTED', xl ('Please select an event') );
define('_EDIT_PC_CONFIG_GLOBAL', xl ('Settings') );
define('_EDIT_PC_CONFIG_DEFAULT', xl ('Use Defaults') );
define('_EDIT_PC_CONFIG_CATEGORIES', xl ('Categories') );
define('_PC_CREATE_EVENT', xl ('Add') );
define('_PC_VIEW_APPROVED', xl ('Approved') );
define('_PC_VIEW_HIDDEN', xl ('Hidden') );
define('_PC_VIEW_QUEUED', xl ('Queued') );
define('_PC_SUBMISSION_ADMIN', xl ('Queued Submissions Administration') );
define('_PC_NEW_SUBMISSIONS', xl ('New Submissions') );
define('_PC_NO_SUBMISSIONS', xl ('There are no New Submissions') );
define('_PC_SUNDAY', xl ('Sunday') );
define('_PC_MONDAY', xl ('Monday') );
define('_PC_SATURDAY', xl ('Saturday') );
define('_PC_ADMIN_SUBMIT', xl ('Commit Changes') );
define('_PC_TIME24HOURS', xl ('Use 24 hour time format?') );
define('_PC_TIME_INCREMENT', xl ('Time Increment for Add (minutes 1-60)') );
define('_PC_EVENTS_IN_NEW_WINDOW', xl ('View events in a popup window?') );
define('_PC_INTERNATIONAL_DATES', xl ('Use international date style?') );
define('_PC_FIRST_DAY_OF_WEEK', xl ('First day of the week') );
define('_PC_TIMES', xl ('Times array (N/A)') );
define('_PC_DAY_HIGHLIGHT_COLOR', xl ('Current day highlight color') );
define('_PC_USE_JS_POPUPS', xl ('Show hovering event text on mouseover?') );
define('_PC_ALLOW_DIRECT_SUBMIT', xl ('Allow submitted events to be made active instantly?') );
define('_PC_ALLOW_SITEWIDE_SUBMIT', xl ('Allow users to publish Global Events') );
define('_PC_ALLOW_USER_CALENDAR', xl ('Allow users to publish Personal Calendars') );
define('_PC_SHOW_EVENTS_IN_YEAR',  xl ('Populate the year view with events?') . xl ('[not recommended]', '' , '<i>', '</i>') );
define('_PC_NUM_COLS_IN_YEAR_VIEW', xl ('Number of columns in year view.') );
define('_PC_UPGRADE_TABLES', xl ('Insert old events into tables') );
define('_PC_LIST_HOW_MANY', xl ('Show how many events on admin pages?') );
define('_PC_USE_CACHE', xl ('Cache template output?') );
define('_PC_CACHE_LIFETIME', xl ('Cache Lifetime (in seconds)') );
define('_PC_DISPLAY_TOPICS', xl ('Use topics?') );
define('_PC_PERFORM_ACTION', xl ('Perform this action') );
define('_PC_ADMIN_ACTION_APPROVE', xl ('Approve') );
define('_PC_ADMIN_ACTION_HIDE', xl ('Hide') );
define('_PC_ADMIN_ACTION_DELETE', xl ('Delete') );
define('_PC_ADMIN_ACTION_EDIT', xl ('Edit') );
define('_PC_ADMIN_ACTION_VIEW', xl ('View') );
define('_PC_EVENTS', xl ('Events') );
define('_PC_NO_EVENTS', xl ('No Events') );
define('_PC_APPROVE_ARE_YOU_SURE', xl ('Are you sure you want to approve these events?') );
define('_PC_HIDE_ARE_YOU_SURE', xl ('Are you sure you want to hide these events?') );
define('_PC_VIEW_ARE_YOU_SURE', xl ('Are you sure you want to view these events?') );
define('_PC_EDIT_ARE_YOU_SURE', xl ('Are you sure you want to edit these events?') );
define('_PC_ADMIN_EVENTS_APPROVED', xl ('The event(s) have been approved.') );
define('_PC_ADMIN_EVENTS_HIDDEN', xl ('The event(s) have been hidden.') );
define('_PC_NEXT', xl ('Next') );
define('_PC_PREV', xl ('Prev') );
define('_PC_EVENT_DATE_FORMAT', xl ('Date Display Format') . xl ('uses php <a href="http://php.net/strftime">strftime</a> format', '', '<i>', '</i>') );
define('_PC_CAT_DELETE', xl ('Delete') );
define('_PC_REP_CAT_TITLE', xl ('Categories') );
define('_PC_REP_CAT_TITLE_S', xl ('Category') );
define('_PC_NEW_CAT_TITLE', xl ('New Categories') );
define('_PC_NEW_CAT_TITLE_S', xl ('New Category') );
define('_PC_ALL_DAY_CAT_TITLE', xl ('All Day') );
define('_PC_ALL_DAY_CAT_YES', xl ('Yes') );
define('_PC_ALL_DAY_CAT_NO', xl ('No') );
define('_PC_CAT_NAME', xl ('Name') );
define('_PC_CAT_NAME_XL', xl ('Name Translation') );
define('_PC_CAT_DESC', xl ('Description') );
define('_PC_CAT_DESC_XL', xl ('Description Translation') );
define('_PC_CAT_COLOR', xl ('Color') );
define('_PC_CAT_DUR', xl ('Duration') );
define('_PC_CAT_NEW', xl ('New =>') );
define('_PC_ARE_YOU_SURE', xl ('Are you sure you\'d like to continue with these actions?') );
define('_PC_DELETE_CATS', xl ('Delete Categories with ID(s) : ') );
define('_PC_ADD_CAT', xl ('Add new category : ') );
define('_PC_MODIFY_CATS', xl ('Make modifications to current categories.') );
define('_PC_CATS_CONFIRM', xl ('YES!') );
define('_PC_DEFAULT_TEMPLATE', xl ('Default Template') );
define('_PC_DEFAULT_VIEW', xl ('Default Calendar View') );
define('_PC_USE_SAFE_MODE', xl ('Is PHP using Safe Mode?') );
define('_PC_CLEAR_CACHE', xl ('Clear Smarty Cache') );
define('_PC_CACHE_CLEARED', xl ('Smarty Cache has been cleared') );
define('_PC_TEST_SYSTEM', xl ('Test System') );
define('_PC_SAFE_MODE_MESSAGE', xl ('Make sure "') ._PC_USE_SAFE_MODE. xl ('" is CHECKED in PostCalendar Settings!') );
define('_EDIT_PC_CONFIG_CATDETAILS', xl ('Category Details') );
define('_PC_DURATION_HOUR', xl ('Hours') );
define('_PC_DURATION_MIN', xl ('Minutes') );
define('_PC_CAT_LIMITS', xl ('Category Limits') );
define('_PC_NEW_LIMIT_TITLE', xl ('New Limit of Events') );
define('_PC_COLOR_PICK_TITLE', xl ('pick') );
define('_PC_CAT_TYPE', xl ('Type') );
?>
