<?php
/**
 * Collect status of a report in report_results sql table.
 *
 * Copyright (C) 2012 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */



require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../report_database.inc");

//  Collect/bookmark a new report id in report_results sql table and send it back.
if (!empty($_POST['status_report_id'])) {
    echo getStatusReportDatabase($_POST['status_report_id']);
} else {
    echo "ERROR";
}
