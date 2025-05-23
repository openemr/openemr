<?php

/**
 * Logout script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tony McCormick <tony@mi-squared.com>
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Set the GET auth parameter to logout.
//  This parameter is then captured in the auth.inc.php script (which is included in globals.php script) and does the following:
//    1. Logs out user
//    2. Closes the php session
//    3. Redirects user to the login screen (maintains the site id)
$_GET['auth'] = "logout";
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization/logout related code
$sessionAllowWrite = true;
require_once("globals.php");
