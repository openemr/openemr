<?php
/**
 * Logout script.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Tony McCormick <tony@mi-squared.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




// Set the GET auth parameter to logout.
//  This parameter is then captured in the auth.inc script (which is included in globals.php script) and does the following:
//    1. Logs out user
//    2. Closes the php session
//    3. Redirects user to the login screen (maintains the site id)
$_GET['auth'] = "logout";
require_once("globals.php");
