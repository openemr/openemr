<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
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
 * @author Cassian LUP <cassi.lup@gmail.com>
 * @author Kevin Yeh <kevin.y@integralemr.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org 
 *
 * Moved out of individual get_* portal functions for re-use by
 * Kevin Yeh (kevin.y@integralemr.com) May 2013
 *
 */
 
// All of the common intialization steps for the get_* patient portal functions are now in this single include.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

//continue session
session_start();

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=".$_SESSION['site_id'];
//

// kick out if patient not authenticated
if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
  $pid = $_SESSION['pid'];
}
else {
  session_destroy();
  header('Location: '.$landingpage.'&w');
  exit;
}
//

$ignoreAuth=true; // ignore the standard authentication for a regular OpenEMR user
require_once(dirname(__file__) . './../interface/globals.php');
?>
