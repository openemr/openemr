<?php
 /**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
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
 * Rewrite and modifications by sjpadgett@gmail.com Padgetts Consulting 2016.
 *
 * @package OpenEMR
 * @author  Medical Information Integration,LLC <info@mi-squared.com>
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */

/* include globals.php, required. */
require_once(dirname(__FILE__) . '/../../globals.php');

/* include api.inc. also required. */
require_once($GLOBALS['srcdir'].'/api.inc');

/* include our smarty derived controller class. */
require('C_FormAnnotate.class.php');

/* Create a form object. */
$c = new C_FormAnnotate();

/* Render a 'new form' page. */
echo $c->default_action();
?>