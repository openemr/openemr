<?php
/**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 *
 *
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
 * @author  Aron Racho <aron@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once(__DIR__.'/../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');

require("C_FormSnellen.class.php");
$c = new C_FormSnellen();
echo $c->default_action_process($_POST);
@formJump();
