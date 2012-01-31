<?php
//  ------------------------------------------------------------------------ //
//                OpenEMR Electronic Medical Records System                  //
//                 Copyright (c) 2012 tajemo.co.za                      //
//                     <http://www.tajemo.co.za/>                            //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA // 
// --------------------------------------------------------------------------//
// Original Author of this file: Craig Bezuidenhout (Tajemo Enterprises)     //
// Purpose of this file: Returns a count of due messages for current user    //
// --------------------------------------------------------------------------//

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false; 

require_once("../../interface/globals.php");  
require_once("$srcdir/htmlspecialchars.inc.php");  
require_once("$srcdir/dated_reminders.php"); 

$dueReminders = GetDueReminderCount(5,strtotime(date('Y/m/d')));
echo ($dueReminders > 0 ? '('.text(intval($dueReminders)).')' : '');
?>
