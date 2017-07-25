<?php
/**
 * Patient matching and selection for the WordPress Patient Portal.
 *
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
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
 * @author  Rod Roark <rod@sunsetsystems.com>
 */




require_once("../globals.php");
require_once("portal.inc.php");

$result = cms_portal_call(array(
  'action'    => 'adduser',
  'newlogin'  => $_REQUEST['login'],
  'newpass'   => $_REQUEST['pass'],
  'newemail'  => $_REQUEST['email'],
));

if ($result['errmsg']) {
    echo xl('Failed to add patient to portal') . ": " . $result['errmsg'];
}
