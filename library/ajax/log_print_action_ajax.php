<?php
/**
 * Copyright (C) 2015 Rod Roark <rod@sunsetsystems.com>
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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

// AJAX handler for logging a printing action.




require_once("../../interface/globals.php");
require_once("$srcdir/log.inc");

$instance = new html2text($_POST['comments']);
$h2t = &$instance;
$h2t->width = 0;
$h2t->_convert(false);

newEvent("print", $_SESSION['authUser'], $_SESSION['authProvider'], 1, $h2t->get_text());
