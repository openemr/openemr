<?php
 /**
 * library/ajax/unset_session_ajax.php Clear active patient on the server side.
 *
 * Copyright (C) 2012 Visolve <services@visolve.com>
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
 * @author  Visolve <services@visolve.com>
 * @link    http://www.open-emr.org
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../interface/globals.php");
require_once("../pid.inc");

//Setpid function is called on receiving an ajax request.
if(($_POST['func']=="unset_pid"))
{
	setpid(0);
}
?> 
