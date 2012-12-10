<?php
/**
 * Returns a count of due messages for current user.
 *
 * Copyright (C) 2012 tajemo.co.za <http://www.tajemo.co.za/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Craig Bezuidenhout <http://www.tajemo.co.za/>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false; 

require_once("../../interface/globals.php");  
require_once("$srcdir/htmlspecialchars.inc.php");  
require_once("$srcdir/dated_reminder_functions.php"); 
require_once("$srcdir/pnotes.inc");

//Collect number of due reminders
$dueReminders = GetDueReminderCount(5,strtotime(date('Y/m/d')));

//Collect number of active messages
$activeMessages = getPnotesByUser("1","no",$_SESSION['authUser'],true);

$totalNumber = $dueReminders + $activeMessages;
echo ($totalNumber > 0 ? '('.text(intval($totalNumber)).')' : '');
?>
