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
//  ok, here's the rest of the language defines
//=========================================================================

define('_PC_UPDATED',                   'Your PostCalendar configuration has been updated.');
define('_PC_UPDATED_DEFAULTS',          'Your PostCalendar configuration has been reset to use defaults.');
define('_POSTCALENDAR',                 'PostCalendar Administration');
define('_PC_ADMIN_GLOBAL_SETTINGS',     '<b>PostCalendar Global Settings</b>');
define('_PC_ADMIN_CATEGORY_SETTINGS',   '<b>PostCalendar Category Settings</b>');
define('_PC_APPROVED_ADMIN',            'Approved Events Administration');
define('_PC_HIDDEN_ADMIN',              'Hidden Events Administration');
define('_PC_QUEUED_ADMIN',              'Queued Events Administration');
define('_PC_NO_EVENT_SELECTED',         'Please select an event');
define('_EDIT_PC_CONFIG_GLOBAL',        'Settings');
define('_EDIT_PC_CONFIG_DEFAULT',       'Use Defaults');
define('_EDIT_PC_CONFIG_CATEGORIES',    'Categories');
define('_PC_CREATE_EVENT',              'Add');
define('_PC_VIEW_APPROVED',             'Approved');
define('_PC_VIEW_HIDDEN',               'Hidden');
define('_PC_VIEW_QUEUED',               'Queued');
define('_PC_SUBMISSION_ADMIN',          'Queued Submissions Administration');
define('_PC_NEW_SUBMISSIONS',           'New Submissions');
define('_PC_NO_SUBMISSIONS',            'There are no New Submissions');
define('_PC_SUNDAY',                    'Sunday');
define('_PC_MONDAY',                    'Monday');
define('_PC_SATURDAY',                  'Saturday');
define('_PC_ADMIN_SUBMIT',              'Commit Changes');
define('_PC_TIME24HOURS',               'Use 24 hour time format?');
define('_PC_TIME_INCREMENT',            'Time Increment for Add (minutes 1-60)');
define('_PC_EVENTS_IN_NEW_WINDOW',      'View events in a popup window?');
define('_PC_INTERNATIONAL_DATES',       'Use international date style?');
define('_PC_FIRST_DAY_OF_WEEK',         'First day of the week');
define('_PC_TIMES',                     'Times array (N/A)');
define('_PC_DAY_HIGHLIGHT_COLOR',       'Current day highlight color');
define('_PC_USE_JS_POPUPS',             'Show hovering event text on mouseover?');
define('_PC_ALLOW_DIRECT_SUBMIT',       'Allow submitted events to be made active instantly?');
define('_PC_ALLOW_SITEWIDE_SUBMIT',     'Allow users to publish Global Events');
define('_PC_ALLOW_USER_CALENDAR',     	'Allow users to publish Personal Calendars');
define('_PC_SHOW_EVENTS_IN_YEAR',       'Populate the year view with events? <i>[not recommended]</i>');
define('_PC_NUM_COLS_IN_YEAR_VIEW',     'Number of columns in year view.');
define('_PC_UPGRADE_TABLES',            'Insert old events into tables');
define('_PC_LIST_HOW_MANY',             'Show how many events on admin pages?');
define('_PC_USE_CACHE',             	'Cache template output?');
define('_PC_CACHE_LIFETIME',            'Cache Lifetime (in seconds)');
define('_PC_DISPLAY_TOPICS',            'Use topics?');
define('_PC_PERFORM_ACTION',            'Perform this action');
define('_PC_ADMIN_ACTION_APPROVE',      'Approve');
define('_PC_ADMIN_ACTION_HIDE',         'Hide');
define('_PC_ADMIN_ACTION_DELETE',       'Delete');
define('_PC_ADMIN_ACTION_EDIT',         'Edit');
define('_PC_ADMIN_ACTION_VIEW',         'View');
define('_PC_EVENTS',                    'Events');
define('_PC_NO_EVENTS',                 'No Events');
define('_PC_APPROVE_ARE_YOU_SURE',      'Are you sure you want to approve these events?');
define('_PC_HIDE_ARE_YOU_SURE',         'Are you sure you want to hide these events?');
define('_PC_VIEW_ARE_YOU_SURE',         'Are you sure you want to view these events?');
define('_PC_EDIT_ARE_YOU_SURE',         'Are you sure you want to edit these events?');
define('_PC_ADMIN_EVENTS_APPROVED',     'The event(s) have been approved.');
define('_PC_ADMIN_EVENTS_HIDDEN',       'The event(s) have been hidden.');
define('_PC_NEXT',                      'Next');
define('_PC_PREV',                      'Prev');
define('_PC_EVENT_DATE_FORMAT',         'Date Display Format <i>uses php <a href="http://php.net/strftime">strftime</a> format</i>');
define('_PC_CAT_DELETE',                'Delete');
define('_PC_REP_CAT_TITLE',             'Categories');
define('_PC_NEW_CAT_TITLE',             'New Categories');
define('_PC_ALL_DAY_CAT_TITLE',         'All Day');
define('_PC_ALL_DAY_CAT_YES',           'Yes');
define('_PC_ALL_DAY_CAT_NO',            'No');
define('_PC_CAT_NAME',                  'Name');
define('_PC_CAT_DESC',                  'Description');
define('_PC_CAT_COLOR',                 'Color');
define('_PC_CAT_DUR',                 	'Duration');
define('_PC_CAT_NEW',                   'New =>');
define('_PC_ARE_YOU_SURE',              'Are you sure you\'d like to continue with these actions?');
define('_PC_DELETE_CATS',               'Delete Categories with ID(s) : ');
define('_PC_ADD_CAT',                   'Add new category : ');
define('_PC_MODIFY_CATS',               'Make modifications to current categories.');
define('_PC_CATS_CONFIRM',              'YES!');
define('_PC_DEFAULT_TEMPLATE',          'Default Template');
define('_PC_DEFAULT_VIEW',          	'Default Calendar View');
define('_PC_USE_SAFE_MODE',          	'Is PHP using Safe Mode?');
define('_PC_CLEAR_CACHE',          		'Clear Smarty Cache');
define('_PC_CACHE_CLEARED',          	'Smarty Cache has been cleared');
define('_PC_TEST_SYSTEM',          		'Test System');
define('_PC_SAFE_MODE_MESSAGE', 		'Make sure "'._PC_USE_SAFE_MODE.'" is CHECKED in PostCalendar Settings!');
define('_EDIT_PC_CONFIG_CATDETAILS',	'Category Details');
define('_PC_DURATION_HOUR',				'Hours');
define('_PC_DURATION_MIN',				'Minutes');
define('_PC_CAT_LIMITS',				'Category Limits');
define('_PC_NEW_LIMIT_TITLE',			'New Limit of Events');
?>
