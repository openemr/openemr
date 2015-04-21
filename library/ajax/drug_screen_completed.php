<?php
/**
 *
 * Drug Screen Complete Update Database
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
 * 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 *
 * Please help the overall project by sending changes you make to the author and to the OpenEMR community.
 *
 */

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../../interface/globals.php");

$drugval = '0';
if ($_POST['testcomplete'] =='true') {
	$drugval = '1';
}

$tracker_id = $_POST['trackerid'];
  if($tracker_id != 0) 
  {  
           sqlStatement("UPDATE patient_tracker SET " .
			   "drug_screen_completed = ? " .
               "WHERE id =? ", array($drugval,$tracker_id));
  }             